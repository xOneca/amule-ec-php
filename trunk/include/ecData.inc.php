<?php
class RLE_Data
{}

class PartFileEncoderData
{}

/* C# Code:
    ///
    /// RLE implementation. I need only decoder part
    ///
    public class RLE_Data {
        bool m_use_diff;
        int m_len;
        byte[] m_enc_buff;
        byte[] m_buff;

        public RLE_Data(int len, bool use_diff)
        {
            m_len = len;
            m_use_diff = use_diff;
            // in worst case 2-byte sequence encoded as 3. So, data can grow at 1/3
            m_enc_buff = new byte[m_len*4/3 + 1];
            m_buff = new byte[m_len];
        }

        public byte[] Buffer
        {
            get { return m_buff; }
        }

        public int Length
        {
            get { return m_len; }
        }

        public void Realloc(int size)
        {
            if ( size == m_len ) {
                return;
            }

            if ( (size > m_len) && (size > m_buff.Length) ) {
                m_buff = new byte[size];
                m_enc_buff = new byte[size * 4 / 3 + 1];
            }
            m_len = size;
        }

        public void Decode(byte [] buff, int start_offset)
        {
            int len = buff.Length;

            int i = start_offset, j = 0;
            while ( j != m_len ) {
                if ( i < (len -1) ) {
                    if (buff[i+1] == buff[i]) {
                        // this is sequence
                        //memset(m_enc_buff + j, buff[i], buff[i + 2]);
                        for(int k = 0; k < buff[i + 2]; k++) {
                            m_enc_buff[j + k] = buff[i];
                        }
                        j += buff[i + 2];
                        i += 3;
                    } else {
                        // this is single byte
                        m_enc_buff[j++] = buff[i++];
                    }
                } else {
                    // only 1 byte left in encoded data - it can't be sequence
                    m_enc_buff[j++] = buff[i++];
                    // if there's no more data, but buffer end is not reached,
                    // it must be error in some point
                    if ( j != m_len ) {
                        Console.WriteLine("RLE_Data: decoding error. {0} bytes decoded to {1} instead of {2}",
                            len, j, m_len);
                        throw new Exception("RLE_Data: decoding error");
                    }
                }
            }
            if ( m_use_diff ) {
                for (int k = 0; k < m_len; k++) {
                    m_buff[k] ^= m_enc_buff[k];
                }
            }
        }
    }

    public class PartFileEncoderData {
        public RLE_Data m_part_status;
        public RLE_Data m_gap_status;

        public PartFileEncoderData(int partcount, int gapcount)
        {
            m_part_status = new RLE_Data(partcount+1, true);
            m_gap_status = new RLE_Data(gapcount*sizeof(Int64)+1, true);
        }

        public void Decode(byte [] gapdata, byte [] partdata)
        {
            m_part_status.Decode(partdata, 0);

            // in a first dword - real size
            //uint32 gapsize = ENDIAN_NTOHL(RawPeekUInt32(gapdata));
            //gapdata += sizeof(uint32);
            //m_gap_status.Realloc(gapsize * 2 * sizeof(uint64));
            Int32 gapsize = System.Net.IPAddress.NetworkToHostOrder(
                (Int32)gapdata[0] | ((Int32)gapdata[1] << 8) |
                ((Int32)gapdata[2] << 16) | ((Int32)gapdata[3] << 24));

            m_gap_status.Realloc(gapsize*2*sizeof(Int64));
            m_gap_status.Decode(gapdata, 4);
        }
    }
*/